'use client'

import { makeStyles, Badge } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    backgroundColor: '#FFFFFF',
    borderRadius: '20px',
    padding: '40px',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
    border: '1px solid rgba(0, 0, 0, 0.08)',
  },
  header: {
    marginBottom: '32px',
    paddingBottom: '24px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.08)',
  },
  title: {
    fontSize: '36px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '8px',
  },
  subtitle: {
    fontSize: '15px',
    color: '#999999',
    fontWeight: 400,
  },
  content: {
    display: 'grid',
    gridTemplateColumns: '1fr 1fr',
    gap: '40px',
    '@media (max-width: 968px)': {
      gridTemplateColumns: '1fr',
    },
  },
  leftSection: {
    display: 'flex',
    flexDirection: 'column',
    gap: '24px',
  },
  metric: {
    display: 'flex',
    flexDirection: 'column',
    gap: '8px',
  },
  metricLabel: {
    fontSize: '14px',
    color: '#999999',
    fontWeight: 600,
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
  },
  metricValue: {
    fontSize: '48px',
    fontWeight: 700,
    color: '#2F6B3C',
    lineHeight: '1',
  },
  metricDetail: {
    fontSize: '15px',
    color: '#666666',
    fontWeight: 400,
  },
  progressBar: {
    height: '16px',
    backgroundColor: '#EEEEEA',
    borderRadius: '12px',
    overflow: 'hidden',
    boxShadow: 'inset 0 1px 2px rgba(0, 0, 0, 0.04)',
    marginTop: '12px',
  },
  progressFill: {
    height: '100%',
    width: '92%',
    backgroundColor: '#66BB6A',
    transition: 'width 0.6s ease',
    borderRadius: '12px',
  },
  statusChips: {
    display: 'grid',
    gridTemplateColumns: 'repeat(2, 1fr)',
    gap: '16px',
    marginTop: '24px',
  },
  statusChip: {
    display: 'flex',
    alignItems: 'center',
    gap: '12px',
    padding: '16px',
    backgroundColor: '#F5F5F0',
    borderRadius: '12px',
    border: '1px solid rgba(0, 0, 0, 0.06)',
  },
  statusIcon: {
    fontSize: '24px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
  },
  statusContent: {
    display: 'flex',
    flexDirection: 'column',
    gap: '4px',
  },
  statusLabel: {
    fontSize: '12px',
    color: '#999999',
    fontWeight: 600,
  },
  statusValue: {
    fontSize: '20px',
    fontWeight: 700,
    color: '#333333',
  },
  rightSection: {
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'center',
    gap: '16px',
  },
  summaryBox: {
    display: 'flex',
    alignItems: 'center',
    gap: '16px',
    padding: '20px',
    backgroundColor: 'rgba(47, 107, 60, 0.05)',
    borderRadius: '14px',
    border: '1px solid rgba(47, 107, 60, 0.1)',
  },
  summaryIcon: {
    fontSize: '32px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
  },
  summaryContent: {
    display: 'flex',
    flexDirection: 'column',
    gap: '4px',
  },
  summaryLabel: {
    fontSize: '13px',
    color: '#666666',
    fontWeight: 600,
  },
  summaryValue: {
    fontSize: '24px',
    fontWeight: 700,
    color: '#2F6B3C',
  },
})

export function AttendanceHeroSection() {
  const styles = useStyles()
  const today = new Date()
  const dateStr = today.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })

  return (
    <div className={styles.root}>
      <div className={styles.header}>
        <div className={styles.title}>Attendance Monitoring</div>
        <div className={styles.subtitle}>Monitor workforce attendance, biometric verification, and site readiness.</div>
      </div>

      <div className={styles.content}>
        <div className={styles.leftSection}>
          <div className={styles.metric}>
            <div className={styles.metricLabel}>Today&apos;s Attendance</div>
            <div style={{ display: 'flex', alignItems: 'baseline', gap: '8px' }}>
              <span className={styles.metricValue}>48</span>
              <span className={styles.metricDetail}>of 51 workers present</span>
            </div>
            <div className={styles.progressBar}>
              <div className={styles.progressFill} />
            </div>
            <div style={{ fontSize: '14px', color: '#666666', marginTop: '8px' }}>92% Attendance Rate</div>
          </div>

          <div className={styles.statusChips}>
            <div className={styles.statusChip}>
              <div className={styles.statusIcon}>✓</div>
              <div className={styles.statusContent}>
                <div className={styles.statusLabel}>Present</div>
                <div className={styles.statusValue}>48</div>
              </div>
            </div>
            <div className={styles.statusChip}>
              <div className={styles.statusIcon}>−</div>
              <div className={styles.statusContent}>
                <div className={styles.statusLabel}>Absent</div>
                <div className={styles.statusValue}>2</div>
              </div>
            </div>
            <div className={styles.statusChip}>
              <div className={styles.statusIcon}>⏱</div>
              <div className={styles.statusContent}>
                <div className={styles.statusLabel}>Late</div>
                <div className={styles.statusValue}>1</div>
              </div>
            </div>
            <div className={styles.statusChip}>
              <div className={styles.statusIcon}>⊘</div>
              <div className={styles.statusContent}>
                <div className={styles.statusLabel}>Pending Scan</div>
                <div className={styles.statusValue}>0</div>
              </div>
            </div>
          </div>
        </div>

        <div className={styles.rightSection}>
          <div className={styles.summaryBox}>
            <div className={styles.summaryIcon}>📍</div>
            <div className={styles.summaryContent}>
              <div className={styles.summaryLabel}>Current Project</div>
              <div className={styles.summaryValue}>Westfield Plaza Development</div>
            </div>
          </div>

          <div className={styles.summaryBox}>
            <div className={styles.summaryIcon}>📅</div>
            <div className={styles.summaryContent}>
              <div className={styles.summaryLabel}>Today</div>
              <div className={styles.summaryValue}>{dateStr}</div>
            </div>
          </div>

          <div className={styles.summaryBox}>
            <div className={styles.summaryIcon}>✓</div>
            <div className={styles.summaryContent}>
              <div className={styles.summaryLabel}>Site Status</div>
              <div className={styles.summaryValue} style={{ color: '#66BB6A' }}>Ready to Operate</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

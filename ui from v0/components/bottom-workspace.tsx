'use client'

import { makeStyles, mergeClasses, tokens, Body2, Caption1 } from '@fluentui/react-components'
import { CheckmarkCircle24Regular, Clock24Regular, Document24Regular, People24Regular, Bookmark24Regular, Warning24Regular } from '@fluentui/react-icons'

const useStyles = makeStyles({
  root: {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(350px, 1fr))',
    gap: tokens.spacingVerticalXL,
    '@media (max-width: 768px)': {
      gridTemplateColumns: '1fr',
    },
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '32px',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
    border: '1px solid rgba(0, 0, 0, 0.08)',
  },
  cardTitle: {
    fontSize: '20px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '6px',
    letterSpacing: '-0.3px',
  },
  cardSubtitle: {
    fontSize: '14px',
    color: '#999999',
    fontWeight: 400,
    marginBottom: '24px',
  },
  warningBanner: {
    backgroundColor: 'rgba(255, 238, 88, 0.12)',
    border: '1px solid rgba(255, 200, 0, 0.2)',
    borderRadius: '12px',
    paddingTop: '14px',
    paddingBottom: '14px',
    paddingLeft: '16px',
    paddingRight: '16px',
    marginBottom: '24px',
    display: 'flex',
    alignItems: 'center',
    gap: '12px',
  },
  warningText: {
    fontSize: '14px',
    color: '#996600',
    fontWeight: 600,
  },
  reportItem: {
    paddingTop: '12px',
    paddingBottom: '12px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.06)',
    '&:last-child': {
      borderBottom: 'none',
    },
  },
  label: {
    fontSize: '12px',
    color: '#999999',
    marginBottom: '6px',
    fontWeight: 600,
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
  },
  value: {
    fontSize: '16px',
    fontWeight: 600,
    color: '#2F6B3C',
  },
  timeline: {
    display: 'flex',
    flexDirection: 'column',
    gap: '12px',
  },
  activity: {
    display: 'flex',
    gap: '14px',
    paddingTop: '12px',
    paddingBottom: '12px',
    paddingLeft: '12px',
    paddingRight: '12px',
    borderRadius: '10px',
    transition: 'all 0.2s ease',
    '&:hover': {
      backgroundColor: 'rgba(0, 0, 0, 0.02)',
    },
  },
  activityIcon: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: '36px',
    height: '36px',
    borderRadius: '50%',
    backgroundColor: 'rgba(102, 187, 106, 0.15)',
    color: '#2F6B3C',
    flexShrink: 0,
    fontSize: '18px',
  },
  activityContent: {
    display: 'flex',
    flexDirection: 'column',
    gap: '3px',
    flex: 1,
  },
  activityTitle: {
    fontSize: '15px',
    fontWeight: 600,
    color: '#333333',
  },
  activityTime: {
    fontSize: '12px',
    color: '#999999',
    fontWeight: 500,
  },
  button: {
    marginTop: '24px',
    paddingTop: '12px',
    paddingBottom: '12px',
    paddingLeft: '24px',
    paddingRight: '24px',
    borderRadius: '10px',
    border: 'none',
    backgroundColor: 'transparent',
    color: '#2F6B3C',
    cursor: 'pointer',
    fontSize: '15px',
    fontWeight: 700,
    transition: 'all 0.25s ease',
    width: '100%',
    height: '48px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    border: '1.5px solid #2F6B3C',
    '&:hover': {
      backgroundColor: 'rgba(47, 107, 60, 0.08)',
      boxShadow: '0 2px 6px rgba(0, 0, 0, 0.08)',
    },
  },
})

export function BottomWorkspace() {
  const styles = useStyles()

  return (
    <div className={styles.root}>
      {/* Daily Report Status */}
      <div className={styles.card}>
        <div className={styles.cardTitle}>Daily Accomplishment Report</div>
        <div className={styles.cardSubtitle}>Have I completed today&apos;s report?</div>

        <div className={styles.warningBanner}>
          <Warning24Regular style={{ fontSize: '20px' }} />
          <span className={styles.warningText}>Remember to submit today&apos;s report before 5 PM</span>
        </div>

        <div className={styles.reportItem}>
          <div className={styles.label}>Today&apos;s Report</div>
          <div className={styles.value}>Pending Submission</div>
        </div>

        <div className={styles.reportItem}>
          <div className={styles.label}>Submission Deadline</div>
          <div className={styles.value}>5:00 PM Today</div>
        </div>

        <div className={styles.reportItem}>
          <div className={styles.label}>Latest Report Date</div>
          <div className={styles.value}>Jun 26, 2024 • 4:45 PM</div>
        </div>

        <div className={styles.reportItem}>
          <div className={styles.label}>Report Content</div>
          <div style={{ fontSize: tokens.fontSizeBase200, color: tokens.colorNeutralForeground1, lineHeight: '1.6' }}>
            <div>• Work Progress</div>
            <div>• Safety Incidents</div>
            <div>• Workforce Summary</div>
            <div>• Equipment Status</div>
          </div>
        </div>

        <button className={styles.button}>View Reports</button>
      </div>

      {/* Recent Activities */}
      <div className={styles.card}>
        <div className={styles.cardTitle}>Site Activity Log</div>
        <div className={styles.cardSubtitle}>What happened recently?</div>

        <div className={styles.timeline}>
          <div className={styles.activity}>
            <div className={styles.activityIcon}>
              <People24Regular />
            </div>
            <div className={styles.activityContent}>
              <div className={styles.activityTitle}>Attendance Recorded</div>
              <div className={styles.activityTime}>Today • 8:30 AM</div>
              <div style={{ fontSize: tokens.fontSizeBase100, color: tokens.colorNeutralForeground3, marginTop: tokens.spacingVerticalXS }}>
                48 out of 51 workers present
              </div>
            </div>
          </div>

          <div className={styles.activity}>
            <div className={styles.activityIcon}>
              <Bookmark24Regular />
            </div>
            <div className={styles.activityContent}>
              <div className={styles.activityTitle}>Phase Updated</div>
              <div className={styles.activityTime}>Yesterday • 3:15 PM</div>
              <div style={{ fontSize: tokens.fontSizeBase100, color: tokens.colorNeutralForeground3, marginTop: tokens.spacingVerticalXS }}>
                Roofing phase now at 65% completion
              </div>
            </div>
          </div>

          <div className={styles.activity}>
            <div className={styles.activityIcon}>
              <Document24Regular />
            </div>
            <div className={styles.activityContent}>
              <div className={styles.activityTitle}>Report Submitted</div>
              <div className={styles.activityTime}>Jun 26 • 4:45 PM</div>
              <div style={{ fontSize: tokens.fontSizeBase100, color: tokens.colorNeutralForeground3, marginTop: tokens.spacingVerticalXS }}>
                Daily accomplishment report approved
              </div>
            </div>
          </div>

          <div className={styles.activity}>
            <div className={styles.activityIcon}>
              <People24Regular />
            </div>
            <div className={styles.activityContent}>
              <div className={styles.activityTitle}>Worker Assigned</div>
              <div className={styles.activityTime}>Jun 25 • 9:20 AM</div>
              <div style={{ fontSize: tokens.fontSizeBase100, color: tokens.colorNeutralForeground3, marginTop: tokens.spacingVerticalXS }}>
                3 new workers assigned to roofing team
              </div>
            </div>
          </div>

          <div className={styles.activity}>
            <div className={styles.activityIcon}>
              <CheckmarkCircle24Regular />
            </div>
            <div className={styles.activityContent}>
              <div className={styles.activityTitle}>Milestone Completed</div>
              <div className={styles.activityTime}>Jun 24 • 2:30 PM</div>
              <div style={{ fontSize: tokens.fontSizeBase100, color: tokens.colorNeutralForeground3, marginTop: tokens.spacingVerticalXS }}>
                Framing phase inspection passed
              </div>
            </div>
          </div>
        </div>

        <button className={styles.button}>View All</button>
      </div>
    </div>
  )
}

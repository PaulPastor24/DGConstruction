'use client'

import { makeStyles, mergeClasses, tokens, Body2, Body1, Caption1 } from '@fluentui/react-components'
import { CheckmarkCircle24Regular, Clock24Regular, CheckmarkCircleFilled24Filled } from '@fluentui/react-icons'

const useStyles = makeStyles({
  root: {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))',
    gap: tokens.spacingVerticalXL,
    '@media (max-width: 1024px)': {
      gridTemplateColumns: 'repeat(2, 1fr)',
    },
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
  workforceItem: {
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: '14px',
    paddingBottom: '14px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.06)',
    '&:last-child': {
      borderBottom: 'none',
    },
  },
  workforceLabel: {
    fontSize: '15px',
    color: '#333333',
    fontWeight: 500,
  },
  workforceValue: {
    fontSize: '18px',
    fontWeight: 700,
    color: '#2F6B3C',
  },
  phaseTracker: {
    display: 'flex',
    flexDirection: 'column',
    gap: '4px',
  },
  phaseStep: {
    display: 'flex',
    alignItems: 'center',
    gap: '16px',
    paddingTop: '10px',
    paddingBottom: '10px',
    paddingLeft: '12px',
    paddingRight: '12px',
    borderRadius: '10px',
    transition: 'all 0.2s ease',
    '&:hover': {
      backgroundColor: 'rgba(47, 107, 60, 0.04)',
    },
  },
  phaseIndicator: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: '36px',
    height: '36px',
    borderRadius: '50%',
    backgroundColor: '#66BB6A',
    color: '#FFFFFF',
    fontWeight: 600,
    fontSize: '16px',
    flexShrink: 0,
    boxShadow: '0 1px 3px rgba(102, 187, 106, 0.3)',
  },
  phaseIndicatorPending: {
    backgroundColor: '#EEEEEA',
    color: '#999999',
    boxShadow: 'none',
    fontWeight: 600,
  },
  phaseIndicatorActive: {
    backgroundColor: '#2F6B3C',
    boxShadow: '0 2px 6px rgba(47, 107, 60, 0.3)',
    fontSize: '16px',
  },
  phaseContent: {
    display: 'flex',
    flexDirection: 'column',
    gap: '3px',
    flex: 1,
  },
  phaseName: {
    fontSize: '15px',
    fontWeight: 600,
    color: '#333333',
  },
  phaseStatus: {
    fontSize: '12px',
    color: '#999999',
    fontWeight: 500,
  },
  milestonTable: {
    display: 'flex',
    flexDirection: 'column',
    gap: 0,
  },
  tableHeader: {
    display: 'grid',
    gridTemplateColumns: '1.5fr 1fr 1fr',
    gap: '16px',
    paddingTop: '12px',
    paddingBottom: '12px',
    paddingLeft: '12px',
    paddingRight: '12px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.08)',
    marginBottom: '8px',
    backgroundColor: 'rgba(47, 107, 60, 0.04)',
    borderRadius: '10px',
  },
  tableHeaderCell: {
    fontSize: '12px',
    fontWeight: 700,
    color: '#2F6B3C',
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
  },
  tableRow: {
    display: 'grid',
    gridTemplateColumns: '1.5fr 1fr 1fr',
    gap: '16px',
    paddingTop: '14px',
    paddingBottom: '14px',
    paddingLeft: '12px',
    paddingRight: '12px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.06)',
    borderRadius: '10px',
    transition: 'all 0.2s ease',
    '&:last-child': {
      borderBottom: 'none',
    },
    '&:hover': {
      backgroundColor: 'rgba(0, 0, 0, 0.02)',
    },
  },
  tableCell: {
    fontSize: '15px',
    color: '#333333',
    fontWeight: 500,
  },
  statusBadge: {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    paddingTop: '6px',
    paddingBottom: '6px',
    paddingLeft: '12px',
    paddingRight: '12px',
    borderRadius: '20px',
    fontSize: '12px',
    fontWeight: 700,
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
    whiteSpace: 'nowrap',
  },
  statusOn: {
    backgroundColor: 'rgba(102, 187, 106, 0.15)',
    color: '#2F6B3C',
  },
  statusWarning: {
    backgroundColor: 'rgba(255, 238, 88, 0.2)',
    color: '#CC6600',
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

export function OperationalWorkspace() {
  const styles = useStyles()

  return (
    <div className={styles.root}>
      {/* Workforce Status */}
      <div className={styles.card}>
        <div className={styles.cardTitle}>Workforce Status</div>
        <div className={styles.cardSubtitle}>Is today&apos;s workforce ready?</div>
        <div className={styles.workforceItem}>
          <span className={styles.workforceLabel}>Present</span>
          <span className={styles.workforceValue}>48</span>
        </div>
        <div className={styles.workforceItem}>
          <span className={styles.workforceLabel}>Absent</span>
          <span className={styles.workforceValue}>2</span>
        </div>
        <div className={styles.workforceItem}>
          <span className={styles.workforceLabel}>Half Day</span>
          <span className={styles.workforceValue}>1</span>
        </div>
        <div className={styles.workforceItem}>
          <span className={styles.workforceLabel}>On Leave</span>
          <span className={styles.workforceValue}>0</span>
        </div>
        <div style={{ marginTop: tokens.spacingVerticalL, paddingTop: tokens.spacingVerticalL, borderTop: `1px solid ${tokens.colorNeutralStroke1}` }}>
          <div style={{ fontSize: tokens.fontSizeBase200, color: tokens.colorNeutralForeground2, marginBottom: tokens.spacingVerticalS }}>Biometric Verification</div>
          <div style={{ display: 'flex', alignItems: 'center', gap: tokens.spacingHorizontalM }}>
            <div
              style={{
                width: '60px',
                height: '60px',
                borderRadius: '50%',
                backgroundColor: '#F5F5F0',
                border: `4px solid #66BB6A`,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: tokens.fontSizeBase500,
                fontWeight: 700,
                color: '#66BB6A',
              }}
            >
              94%
            </div>
          </div>
        </div>
        <button className={styles.button}>Manage Attendance</button>
      </div>

      {/* Construction Phases */}
      <div className={styles.card}>
        <div className={styles.cardTitle}>Current Site Phase</div>
        <div className={styles.cardSubtitle}>What phase is currently active?</div>
        <div className={styles.phaseTracker}>
          <div className={styles.phaseStep}>
            <div className={mergeClasses(styles.phaseIndicator)}>✓</div>
            <div className={styles.phaseContent}>
              <div className={styles.phaseName}>Foundation</div>
              <div className={styles.phaseStatus}>Completed</div>
            </div>
          </div>
          <div className={styles.phaseStep}>
            <div className={mergeClasses(styles.phaseIndicator)}>✓</div>
            <div className={styles.phaseContent}>
              <div className={styles.phaseName}>Framing</div>
              <div className={styles.phaseStatus}>Completed</div>
            </div>
          </div>
          <div className={styles.phaseStep}>
            <div className={mergeClasses(styles.phaseIndicator, styles.phaseIndicatorActive)}>◉</div>
            <div className={styles.phaseContent}>
              <div className={styles.phaseName}>Roofing</div>
              <div className={styles.phaseStatus}>Active - 65% Complete</div>
            </div>
          </div>
          <div className={styles.phaseStep}>
            <div className={mergeClasses(styles.phaseIndicator, styles.phaseIndicatorPending)}>4</div>
            <div className={styles.phaseContent}>
              <div className={styles.phaseName}>Finishing</div>
              <div className={styles.phaseStatus}>Pending</div>
            </div>
          </div>
          <div className={styles.phaseStep}>
            <div className={mergeClasses(styles.phaseIndicator, styles.phaseIndicatorPending)}>5</div>
            <div className={styles.phaseContent}>
              <div className={styles.phaseName}>Inspection</div>
              <div className={styles.phaseStatus}>Pending</div>
            </div>
          </div>
        </div>
      </div>

      {/* Upcoming Milestones */}
      <div className={styles.card}>
        <div className={styles.cardTitle}>Upcoming Milestones</div>
        <div className={styles.cardSubtitle}>What deadlines require attention?</div>
        <div className={styles.milestonTable}>
          <div className={styles.tableHeader}>
            <div className={styles.tableHeaderCell}>Milestone</div>
            <div className={styles.tableHeaderCell}>Target Date</div>
            <div className={styles.tableHeaderCell}>Status</div>
          </div>
          <div className={styles.tableRow}>
            <div className={styles.tableCell}>Roofing Inspection</div>
            <div className={styles.tableCell}>Jun 28</div>
            <div className={mergeClasses(styles.statusBadge, styles.statusWarning)}>Warning</div>
          </div>
          <div className={styles.tableRow}>
            <div className={styles.tableCell}>Final Inspection</div>
            <div className={styles.tableCell}>Jul 15</div>
            <div className={mergeClasses(styles.statusBadge, styles.statusOn)}>On Track</div>
          </div>
          <div className={styles.tableRow}>
            <div className={styles.tableCell}>Punch List</div>
            <div className={styles.tableCell}>Jul 22</div>
            <div className={mergeClasses(styles.statusBadge, styles.statusOn)}>On Track</div>
          </div>
          <div className={styles.tableRow}>
            <div className={styles.tableCell}>Client Handover</div>
            <div className={styles.tableCell}>Jul 29</div>
            <div className={mergeClasses(styles.statusBadge, styles.statusOn)}>On Track</div>
          </div>
          <div className={styles.tableRow}>
            <div className={styles.tableCell}>Project Closure</div>
            <div className={styles.tableCell}>Aug 05</div>
            <div className={mergeClasses(styles.statusBadge, styles.statusOn)}>On Track</div>
          </div>
        </div>
        <button className={styles.button}>View Timeline</button>
      </div>
    </div>
  )
}
